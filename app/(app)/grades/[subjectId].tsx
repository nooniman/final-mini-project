/**
 * Grade Detail Screen
 * Shows detailed information about a specific subject's grade
 */

import React, { useEffect, useState } from 'react';
import { View, StyleSheet, ScrollView, ActivityIndicator } from 'react-native';
import { useLocalSearchParams, router } from 'expo-router';
import { Ionicons } from '@expo/vector-icons';
import { useTheme } from '../../../src/context/ThemeContext';
import { Layout } from '../../../src/constants/Layout';
import { gradesApi } from '../../../src/services/api';
import type { GradeDetails } from '../../../src/types/grades';
import {
  ThemedView,
  ThemedText,
  Card,
  Badge,
  Header,
} from '../../../src/components/ui';

export default function GradeDetailScreen() {
  const { colors } = useTheme();
  const { subjectId } = useLocalSearchParams<{ subjectId: string }>();
  const [grade, setGrade] = useState<GradeDetails | null>(null);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    const fetchGradeDetails = async () => {
      try {
        setIsLoading(true);
        const response = await gradesApi.getBySubject(subjectId);
        if (response.success && response.data) {
          setGrade(response.data);
        }
      } catch (error) {
        console.error('Failed to fetch grade details:', error);
      } finally {
        setIsLoading(false);
      }
    };

    if (subjectId) {
      fetchGradeDetails();
    }
  }, [subjectId]);

  const getGradeColor = (gradeValue: number) => {
    if (gradeValue <= 1.5) return colors.success;
    if (gradeValue <= 2.0) return colors.info;
    if (gradeValue <= 2.5) return colors.warning;
    return colors.error;
  };

  if (isLoading) {
    return (
      <ThemedView style={styles.container}>
        <Header title="Grade Details" showBack />
        <View style={[styles.container, { justifyContent: 'center', alignItems: 'center' }]}>
          <ActivityIndicator size="large" color={colors.primary} />
        </View>
      </ThemedView>
    );
  }

  if (!grade) {
    return (
      <ThemedView style={styles.container}>
        <Header title="Grade Details" showBack />
        <View style={{ flex: 1, justifyContent: 'center', alignItems: 'center', padding: 32 }}>
          <Ionicons name="alert-circle-outline" size={64} color={colors.textSecondary} />
          <ThemedText variant="headline" style={{ color: colors.textSecondary, marginTop: 16 }}>
            Grade not found
          </ThemedText>
        </View>
      </ThemedView>
    );
  }

  return (
    <ThemedView style={styles.container}>
      <Header
        title="Grade Details"
        showBack
        onBackPress={() => router.back()}
      />

      <ScrollView
        style={styles.scrollView}
        contentContainerStyle={styles.scrollContent}
        showsVerticalScrollIndicator={false}
      >
        {/* Main Grade Card */}
        <Card variant="elevated" style={styles.mainCard}>
          <View style={styles.subjectHeader}>
            <View>
              <ThemedText variant="title2">{grade.subjectName}</ThemedText>
              <ThemedText variant="subheadline" color="secondary">
                {grade.subjectCode} • {grade.units} units
              </ThemedText>
            </View>
          </View>

          <View style={[styles.divider, { backgroundColor: colors.divider }]} />

          <View style={styles.gradeDisplay}>
            <View style={styles.gradeMain}>
              <ThemedText
                variant="title1"
                style={[
                  styles.gradeValue,
                  { color: getGradeColor(grade.grade) },
                ]}
              >
                {grade.grade.toFixed(2)}
              </ThemedText>
              <Badge
                label={grade.remarks}
                variant={grade.remarks === 'Passed' ? 'success' : 'error'}
              />
            </View>
          </View>

          {(grade.midtermGrade || grade.finalGrade) && (
            <>
              <View style={[styles.divider, { backgroundColor: colors.divider }]} />
              <View style={styles.gradeBreakdown}>
                {grade.midtermGrade && (
                  <View style={styles.gradeItem}>
                    <ThemedText variant="caption1" color="secondary">
                      Midterm
                    </ThemedText>
                    <ThemedText variant="headline">{grade.midtermGrade.toFixed(2)}</ThemedText>
                  </View>
                )}
                {grade.midtermGrade && grade.finalGrade && (
                  <View
                    style={[
                      styles.gradeItemDivider,
                      { backgroundColor: colors.divider },
                    ]}
                  />
                )}
                {grade.finalGrade && (
                  <View style={styles.gradeItem}>
                    <ThemedText variant="caption1" color="secondary">
                      Final
                    </ThemedText>
                    <ThemedText variant="headline">{grade.finalGrade.toFixed(2)}</ThemedText>
                  </View>
                )}
              </View>
            </>
          )}
        </Card>

        {/* Grade Components */}
        {grade.components && grade.components.length > 0 && (
          <View style={styles.section}>
            <ThemedText variant="title3" style={styles.sectionTitle}>
              Grade Components
            </ThemedText>
            <Card variant="elevated">
              {grade.components.map((component: any, index: number) => (
                <View key={index}>
                  <View style={styles.componentRow}>
                    <View style={styles.componentInfo}>
                      <ThemedText variant="headline">{component.name}</ThemedText>
                      <ThemedText variant="caption1" color="secondary">
                        {component.weight}% weight
                      </ThemedText>
                    </View>
                    <View style={styles.componentScore}>
                      <ThemedText variant="headline">
                        {component.score}/{component.maxScore}
                      </ThemedText>
                      <ThemedText variant="caption1" color="secondary">
                        {component.percentage}%
                      </ThemedText>
                    </View>
                  </View>
                  {index !== grade.components.length - 1 && (
                    <View
                      style={[
                        styles.componentDivider,
                        { backgroundColor: colors.divider },
                      ]}
                    />
                  )}
                </View>
              ))}
            </Card>
          </View>
        )}

        {/* Instructor */}
        <View style={styles.section}>
          <ThemedText variant="title3" style={styles.sectionTitle}>
            Instructor
          </ThemedText>
          <Card variant="elevated" padding="md">
            <View style={styles.instructorRow}>
              <View
                style={[
                  styles.instructorAvatar,
                  { backgroundColor: colors.primaryMuted },
                ]}
              >
                <Ionicons name="person" size={24} color={colors.primary} />
              </View>
              <View style={styles.instructorInfo}>
                <ThemedText variant="headline">{grade.instructor}</ThemedText>
                <ThemedText variant="caption1" color="secondary">
                  Course Instructor
                </ThemedText>
              </View>
            </View>
          </Card>
        </View>

        {/* Additional Info */}
        <Card variant="elevated" style={styles.metaCard} padding="md">
          {grade.classStanding && (
            <>
              <View style={styles.metaRow}>
                <Ionicons name="trophy-outline" size={18} color={colors.textTertiary} />
                <ThemedText variant="body" color="secondary">
                  Class Standing: #{grade.classStanding}
                </ThemedText>
              </View>
              <View style={[styles.divider, { backgroundColor: colors.divider, marginVertical: 8 }]} />
            </>
          )}
          {grade.datePosted && (
            <View style={styles.metaRow}>
              <Ionicons name="calendar-outline" size={18} color={colors.textTertiary} />
              <ThemedText variant="body" color="secondary">
                Posted on {new Date(grade.datePosted).toLocaleDateString('en-US', {
                  year: 'numeric',
                  month: 'long',
                  day: 'numeric',
                })}
              </ThemedText>
            </View>
          )}
        </Card>

        <View style={{ height: Layout.spacing.xl }} />
      </ScrollView>
    </ThemedView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
  scrollView: {
    flex: 1,
  },
  scrollContent: {
    paddingHorizontal: Layout.padding.horizontal,
    paddingTop: Layout.spacing.md,
  },
  mainCard: {
    marginBottom: Layout.spacing.lg,
  },
  subjectHeader: {
    padding: Layout.spacing.md,
  },
  divider: {
    height: StyleSheet.hairlineWidth,
  },
  gradeDisplay: {
    alignItems: 'center',
    paddingVertical: Layout.spacing.lg,
  },
  gradeMain: {
    alignItems: 'center',
  },
  gradeValue: {
    fontSize: 64,
    fontWeight: '700',
    lineHeight: 72,
  },
  gradeBreakdown: {
    flexDirection: 'row',
  },
  gradeItem: {
    flex: 1,
    alignItems: 'center',
    paddingVertical: Layout.spacing.md,
  },
  gradeItemDivider: {
    width: 1,
  },
  section: {
    marginBottom: Layout.spacing.lg,
  },
  sectionTitle: {
    marginBottom: Layout.spacing.sm,
  },
  componentRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: Layout.spacing.md,
  },
  componentInfo: {
    flex: 1,
  },
  componentScore: {
    alignItems: 'flex-end',
  },
  componentDivider: {
    height: StyleSheet.hairlineWidth,
    marginLeft: Layout.spacing.md,
  },
  instructorRow: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  instructorAvatar: {
    width: 48,
    height: 48,
    borderRadius: 24,
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: Layout.spacing.md,
  },
  instructorInfo: {
    flex: 1,
  },
  metaCard: {
    marginBottom: Layout.spacing.lg,
  },
  metaRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: Layout.spacing.sm,
  },
});
