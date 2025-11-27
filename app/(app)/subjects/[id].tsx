/**
 * Subject Detail Screen
 * Shows detailed information about a specific subject
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

// Mock subject details
const mockSubjectDetails = {
  id: '1',
  code: 'CS 201',
  name: 'Data Structures and Algorithms',
  description: 'This course covers fundamental data structures such as arrays, linked lists, stacks, queues, trees, and graphs. Students will learn algorithm analysis and design techniques including sorting, searching, and graph algorithms.',
  units: 3,
  lectureHours: 2,
  labHours: 3,
  instructor: {
    name: 'Dr. Juan Dela Cruz',
    email: 'juan.delacruz@wmsu.edu.ph',
    department: 'Computer Science Department',
    office: 'CCS Building, Room 301',
    consultationHours: 'MWF 2:00 PM - 4:00 PM',
  },
  schedule: [
    { day: 'Monday', time: '8:00 AM - 9:30 AM', room: 'CL-301', type: 'Lecture' },
    { day: 'Wednesday', time: '8:00 AM - 9:30 AM', room: 'CL-301', type: 'Lecture' },
    { day: 'Friday', time: '8:00 AM - 11:00 AM', room: 'CL-Lab1', type: 'Laboratory' },
  ],
  prerequisites: ['CS 101 - Introduction to Programming', 'MATH 101 - Calculus I'],
  classSize: 42,
  enrolledCount: 38,
};

export default function SubjectDetailScreen() {
  const { colors } = useTheme();
  const { id } = useLocalSearchParams<{ id: string }>();

  return (
    <ThemedView style={styles.container}>
      <Header
        title="Subject Details"
        showBack
        onBackPress={() => router.back()}
      />

      <ScrollView
        style={styles.scrollView}
        contentContainerStyle={styles.scrollContent}
        showsVerticalScrollIndicator={false}
      >
        {/* Subject Header Card */}
        <Card variant="elevated" style={styles.headerCard}>
          <View style={styles.subjectHeader}>
            <Badge label={mockSubjectDetails.code} variant="primary" />
            <ThemedText variant="title2" style={styles.subjectName}>
              {mockSubjectDetails.name}
            </ThemedText>
            <View style={styles.unitsBadge}>
              <ThemedText variant="subheadline" color="secondary">
                {mockSubjectDetails.units} units ({mockSubjectDetails.lectureHours} lec, {mockSubjectDetails.labHours} lab)
              </ThemedText>
            </View>
          </View>

          <View style={[styles.divider, { backgroundColor: colors.divider }]} />

          <View style={styles.statsRow}>
            <View style={styles.statItem}>
              <Ionicons name="people-outline" size={20} color={colors.primary} />
              <ThemedText variant="headline">{mockSubjectDetails.enrolledCount}</ThemedText>
              <ThemedText variant="caption2" color="secondary">Enrolled</ThemedText>
            </View>
            <View style={[styles.statDivider, { backgroundColor: colors.divider }]} />
            <View style={styles.statItem}>
              <Ionicons name="resize-outline" size={20} color={colors.primary} />
              <ThemedText variant="headline">{mockSubjectDetails.classSize}</ThemedText>
              <ThemedText variant="caption2" color="secondary">Capacity</ThemedText>
            </View>
          </View>
        </Card>

        {/* Description */}
        <View style={styles.section}>
          <ThemedText variant="title3" style={styles.sectionTitle}>
            Description
          </ThemedText>
          <Card variant="elevated" padding="md">
            <ThemedText variant="body" color="secondary" style={styles.description}>
              {mockSubjectDetails.description}
            </ThemedText>
          </Card>
        </View>

        {/* Schedule */}
        <View style={styles.section}>
          <ThemedText variant="title3" style={styles.sectionTitle}>
            Schedule
          </ThemedText>
          <Card variant="elevated">
            {mockSubjectDetails.schedule.map((sched, index) => (
              <View key={index}>
                <View style={styles.scheduleRow}>
                  <View style={[styles.dayBadge, { backgroundColor: colors.primaryMuted }]}>
                    <ThemedText variant="caption1" color="accent" weight="semibold">
                      {sched.day.substring(0, 3).toUpperCase()}
                    </ThemedText>
                  </View>
                  <View style={styles.scheduleInfo}>
                    <ThemedText variant="headline">{sched.time}</ThemedText>
                    <View style={styles.scheduleDetails}>
                      <Ionicons name="location-outline" size={14} color={colors.textTertiary} />
                      <ThemedText variant="caption1" color="secondary">
                        {sched.room}
                      </ThemedText>
                      <Badge
                        label={sched.type}
                        variant={sched.type === 'Laboratory' ? 'info' : 'secondary'}
                        size="sm"
                      />
                    </View>
                  </View>
                </View>
                {index !== mockSubjectDetails.schedule.length - 1 && (
                  <View style={[styles.scheduleDivider, { backgroundColor: colors.divider }]} />
                )}
              </View>
            ))}
          </Card>
        </View>

        {/* Instructor */}
        <View style={styles.section}>
          <ThemedText variant="title3" style={styles.sectionTitle}>
            Instructor
          </ThemedText>
          <Card variant="elevated" padding="md">
            <View style={styles.instructorHeader}>
              <View style={[styles.instructorAvatar, { backgroundColor: colors.primaryMuted }]}>
                <Ionicons name="person" size={28} color={colors.primary} />
              </View>
              <View style={styles.instructorInfo}>
                <ThemedText variant="headline">{mockSubjectDetails.instructor.name}</ThemedText>
                <ThemedText variant="caption1" color="secondary">
                  {mockSubjectDetails.instructor.department}
                </ThemedText>
              </View>
            </View>

            <View style={[styles.divider, { backgroundColor: colors.divider, marginVertical: Layout.spacing.md }]} />

            <View style={styles.contactInfo}>
              <View style={styles.contactRow}>
                <Ionicons name="mail-outline" size={18} color={colors.textTertiary} />
                <ThemedText variant="subheadline" color="secondary">
                  {mockSubjectDetails.instructor.email}
                </ThemedText>
              </View>
              <View style={styles.contactRow}>
                <Ionicons name="location-outline" size={18} color={colors.textTertiary} />
                <ThemedText variant="subheadline" color="secondary">
                  {mockSubjectDetails.instructor.office}
                </ThemedText>
              </View>
              <View style={styles.contactRow}>
                <Ionicons name="time-outline" size={18} color={colors.textTertiary} />
                <ThemedText variant="subheadline" color="secondary">
                  {mockSubjectDetails.instructor.consultationHours}
                </ThemedText>
              </View>
            </View>
          </Card>
        </View>

        {/* Prerequisites */}
        <View style={styles.section}>
          <ThemedText variant="title3" style={styles.sectionTitle}>
            Prerequisites
          </ThemedText>
          <Card variant="elevated">
            {mockSubjectDetails.prerequisites.map((prereq, index) => (
              <View key={index}>
                <View style={styles.prereqRow}>
                  <Ionicons name="checkmark-circle" size={20} color={colors.success} />
                  <ThemedText variant="body" style={styles.prereqText}>
                    {prereq}
                  </ThemedText>
                </View>
                {index !== mockSubjectDetails.prerequisites.length - 1 && (
                  <View style={[styles.prereqDivider, { backgroundColor: colors.divider }]} />
                )}
              </View>
            ))}
          </Card>
        </View>

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
  headerCard: {
    marginBottom: Layout.spacing.lg,
  },
  subjectHeader: {
    padding: Layout.spacing.md,
  },
  subjectName: {
    marginTop: Layout.spacing.sm,
    marginBottom: Layout.spacing.xs,
  },
  unitsBadge: {},
  divider: {
    height: StyleSheet.hairlineWidth,
  },
  statsRow: {
    flexDirection: 'row',
  },
  statItem: {
    flex: 1,
    alignItems: 'center',
    paddingVertical: Layout.spacing.md,
    gap: 2,
  },
  statDivider: {
    width: 1,
  },
  section: {
    marginBottom: Layout.spacing.lg,
  },
  sectionTitle: {
    marginBottom: Layout.spacing.sm,
  },
  description: {
    lineHeight: 24,
  },
  scheduleRow: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: Layout.spacing.md,
  },
  dayBadge: {
    width: 48,
    height: 48,
    borderRadius: 8,
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: Layout.spacing.md,
  },
  scheduleInfo: {
    flex: 1,
  },
  scheduleDetails: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
    marginTop: 4,
  },
  scheduleDivider: {
    height: StyleSheet.hairlineWidth,
    marginLeft: 76,
  },
  instructorHeader: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  instructorAvatar: {
    width: 56,
    height: 56,
    borderRadius: 28,
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: Layout.spacing.md,
  },
  instructorInfo: {
    flex: 1,
  },
  contactInfo: {
    gap: Layout.spacing.sm,
  },
  contactRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: Layout.spacing.sm,
  },
  prereqRow: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: Layout.spacing.md,
  },
  prereqText: {
    marginLeft: Layout.spacing.sm,
    flex: 1,
  },
  prereqDivider: {
    height: StyleSheet.hairlineWidth,
    marginLeft: 44,
  },
});
