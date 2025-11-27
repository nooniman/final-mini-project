/**
 * Grade Detail Screen
 * Shows detailed information about a specific subject's grade
 */

import React from 'react';
import { View, StyleSheet, ScrollView } from 'react-native';
import { useLocalSearchParams, router } from 'expo-router';
import { Ionicons } from '@expo/vector-icons';
import { useTheme } from '../../../src/context/ThemeContext';
import { Layout } from '../../../src/constants/Layout';
import {
  ThemedView,
  ThemedText,
  Card,
  Badge,
  Header,
} from '../../../src/components/ui';

// Mock grade details
const mockGradeDetails = {
  id: '1',
  code: 'CS 201',
  name: 'Data Structures and Algorithms',
  units: 3,
  instructor: 'Dr. Juan Dela Cruz',
  midtermGrade: 1.25,
  finalGrade: 1.25,
  grade: 1.25,
  remarks: 'Passed',
  components: [
    { name: 'Quizzes', score: 92, maxScore: 100, weight: 20 },
    { name: 'Assignments', score: 88, maxScore: 100, weight: 15 },
    { name: 'Midterm Exam', score: 85, maxScore: 100, weight: 25 },
    { name: 'Final Exam', score: 90, maxScore: 100, weight: 30 },
    { name: 'Class Participation', score: 95, maxScore: 100, weight: 10 },
  ],
  classStanding: 5,
  totalStudents: 42,
  datePosted: '2024-11-20',
};

export default function GradeDetailScreen() {
  const { colors } = useTheme();
  const { subjectId } = useLocalSearchParams<{ subjectId: string }>();

  const getGradeColor = (grade: number) => {
    if (grade <= 1.5) return colors.success;
    if (grade <= 2.0) return colors.info;
    if (grade <= 2.5) return colors.warning;
    return colors.error;
  };

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
              <ThemedText variant="title2">{mockGradeDetails.name}</ThemedText>
              <ThemedText variant="subheadline" color="secondary">
                {mockGradeDetails.code} • {mockGradeDetails.units} units
              </ThemedText>
            </View>
          </View>

          <View style={[styles.divider, { backgroundColor: colors.divider }]} />

          <View style={styles.gradeDisplay}>
            <View style={styles.gradeMain}>
              <ThemedText variant="caption1" color="secondary">
                Final Grade
              </ThemedText>
              <ThemedText
                style={[styles.gradeValue, { color: getGradeColor(mockGradeDetails.grade) }]}
              >
                {mockGradeDetails.grade.toFixed(2)}
              </ThemedText>
              <Badge label={mockGradeDetails.remarks} variant="success" />
            </View>
          </View>

          <View style={styles.gradeBreakdown}>
            <View style={styles.gradeItem}>
              <ThemedText variant="caption1" color="secondary">
                Midterm
              </ThemedText>
              <ThemedText variant="title3">
                {mockGradeDetails.midtermGrade.toFixed(2)}
              </ThemedText>
            </View>
            <View style={[styles.gradeItemDivider, { backgroundColor: colors.divider }]} />
            <View style={styles.gradeItem}>
              <ThemedText variant="caption1" color="secondary">
                Final
              </ThemedText>
              <ThemedText variant="title3">
                {mockGradeDetails.finalGrade.toFixed(2)}
              </ThemedText>
            </View>
            <View style={[styles.gradeItemDivider, { backgroundColor: colors.divider }]} />
            <View style={styles.gradeItem}>
              <ThemedText variant="caption1" color="secondary">
                Rank
              </ThemedText>
              <ThemedText variant="title3">
                #{mockGradeDetails.classStanding}
              </ThemedText>
            </View>
          </View>
        </Card>

        {/* Grade Components */}
        <View style={styles.section}>
          <ThemedText variant="title3" style={styles.sectionTitle}>
            Grade Components
          </ThemedText>

          <Card variant="elevated">
            {mockGradeDetails.components.map((component, index) => (
              <View key={index}>
                <View style={styles.componentRow}>
                  <View style={styles.componentInfo}>
                    <ThemedText variant="body">{component.name}</ThemedText>
                    <ThemedText variant="caption1" color="secondary">
                      Weight: {component.weight}%
                    </ThemedText>
                  </View>
                  <View style={styles.componentScore}>
                    <ThemedText variant="headline">
                      {component.score}/{component.maxScore}
                    </ThemedText>
                    <ThemedText variant="caption1" color="secondary">
                      {((component.score / component.maxScore) * 100).toFixed(0)}%
                    </ThemedText>
                  </View>
                </View>
                {index !== mockGradeDetails.components.length - 1 && (
                  <View style={[styles.componentDivider, { backgroundColor: colors.divider }]} />
                )}
              </View>
            ))}
          </Card>
        </View>

        {/* Instructor Info */}
        <View style={styles.section}>
          <ThemedText variant="title3" style={styles.sectionTitle}>
            Instructor
          </ThemedText>

          <Card variant="elevated" padding="md">
            <View style={styles.instructorRow}>
              <View style={[styles.instructorAvatar, { backgroundColor: colors.primaryMuted }]}>
                <Ionicons name="person" size={24} color={colors.primary} />
              </View>
              <View style={styles.instructorInfo}>
                <ThemedText variant="headline">{mockGradeDetails.instructor}</ThemedText>
                <ThemedText variant="caption1" color="secondary">
                  Computer Science Department
                </ThemedText>
              </View>
            </View>
          </Card>
        </View>

        {/* Meta Info */}
        <Card variant="outlined" padding="md" style={styles.metaCard}>
          <View style={styles.metaRow}>
            <Ionicons name="calendar-outline" size={16} color={colors.textTertiary} />
            <ThemedText variant="caption1" color="tertiary">
              Grade posted on {new Date(mockGradeDetails.datePosted).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
              })}
            </ThemedText>
          </View>
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
    borderTopWidth: StyleSheet.hairlineWidth,
    borderTopColor: '#E0E0E0',
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
